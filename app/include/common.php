<?php

/**
 * Establishes a database connection using environment variables.
 *
 * @return PDO
 */
function create_pdo(): PDO
{
    [
        'MYSQL_HOST' => $host,
        'MYSQL_DATABASE' => $dbname,
        'MYSQL_USER' => $user,
        'MYSQL_PASSWORD' => $pass,
    ] = $_ENV;

    return new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
}

/**
 * Generates a specified number of promotional codes.
 *
 * @param PDO $pdo A PDO instance for database connection.
 * @param int $count The number of promotional codes to generate.
 * @return bool Returns true on success or false on failure.
 */
function generate_promocodes(PDO $pdo, int $count): bool
{
    return $pdo->prepare("CALL GENERATE_PROMOCODES(?)")->execute([$count]);
}

/**
 * Finds a promotional code associated with a specific user UUID.
 *
 * @param PDO $pdo A PDO instance for database connection.
 * @param string $user_uuid_binary The binary representation of the user's UUID.
 * @return string Returns the promotional code as a base64 encoded string, or an empty string if no code is found.
 */
function find_promocode_by_user_uuid(PDO $pdo, string $user_uuid_binary): string
{
    $stmt = $pdo->prepare("SELECT TO_BASE64(code) FROM promocode WHERE user_uuid = ?");
    $stmt->execute([$user_uuid_binary]);
    return $stmt->fetchColumn();
}

/**
 * Counts the occurrences of a specific IP address in the promocode table.
 *
 * @param PDO $pdo A PDO instance for database connection.
 * @param string $ip The IP address to count in the database.
 * @return int The number of occurrences of the specified IP address.
 */
function count_ip(PDO $pdo, string $ip): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM promocode WHERE user_ip = INET6_ATON(?)");
    $stmt->execute([$ip]);
    return (int) $stmt->fetchColumn();
}

/**
 * Assigns an available promotional code to a user and records the associated information.
 *
 * @param PDO $pdo A PDO instance for database connection.
 * @param string $user_uuid_binary The binary UUID of the user to whom the promotional code is being assigned.
 * @param string $ip The IP address of the user making the request.
 * @return string Returns the assigned promotional code as a Base64-encoded string, or an empty string if no code is available.
 */
function assign_promocode_to_user(PDO $pdo, string $user_uuid_binary, string $ip): string
{
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        "SELECT TO_BASE64(code) 
        FROM promocode 
        WHERE user_uuid IS NULL 
        LIMIT 1 FOR UPDATE"
    );
    $stmt->execute();
    $code = $stmt->fetchColumn();
    if (!$code) {
        $pdo->rollBack();
        return '';
    }

    $stmt = $pdo->prepare(
        "UPDATE promocode 
        SET user_uuid = ?,
            user_ip = INET6_ATON(?),
            issue_date = NOW()
        WHERE code = FROM_BASE64(?)"
    );
    $stmt->execute([$user_uuid_binary, $ip, $code]);
    $pdo->commit();

    return $code;
}

/**
 * Counts the total number of promocodes in the database.
 *
 * @param PDO $pdo The PDO database connection instance.
 * @return int The total count of promocodes.
 */
function count_promocodes(PDO $pdo): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM promocode");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/**
 * Retrieves a paginated list of promocodes from the database.
 *
 * @param PDO $pdo The PDO database connection instance.
 * @param int $page The page number to retrieve, default is 1.
 * @param int $per_page The number of records to retrieve per page, default is 100.
 * @return array An associative array of paginated promocode records.
 */
function paginate_promocodes(PDO $pdo, int $page = 1, int $per_page = 100): array
{
    $stmt = $pdo->prepare(
        "SELECT
            TO_BASE64(code) AS code, 
            BIN_TO_UUID(user_uuid) AS user_uuid,
            INET6_NTOA(user_ip) AS user_ip,
            issue_date
        FROM promocode
        ORDER BY issue_date DESC 
        LIMIT :limit OFFSET :offset"
    );
    $stmt->bindValue(':offset', ($page - 1) * $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
