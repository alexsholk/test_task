CREATE TABLE promocode (
    code BINARY(6) NOT NULL PRIMARY KEY,
    issue_date TIMESTAMP NULL,
    user_uuid BINARY(16) NULL,
    user_ip VARBINARY(16) NULL,
    UNIQUE INDEX idx_user_uuid (user_uuid),
    INDEX idx_user_ip (user_ip)
);


DELIMITER //
CREATE PROCEDURE GENERATE_PROMOCODES(IN count INT)
BEGIN
    DECLARE i INT DEFAULT 0;

    CREATE TEMPORARY TABLE temp_promocodes (
        code BINARY(6) UNIQUE
    );

    WHILE i < count DO
        INSERT IGNORE INTO temp_promocodes (code) VALUES (RANDOM_BYTES(6));
        SET i = i + 1;
    END WHILE;

    INSERT IGNORE INTO promocode (code)
    SELECT code FROM temp_promocodes;

    DROP TABLE temp_promocodes;
END;
//
DELIMITER ;


CALL GENERATE_PROMOCODES(5e5);