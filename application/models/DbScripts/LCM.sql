CREATE TABLE users (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    user_type INTEGER NOT NULL,
    email VARCHAR(256),
    password VARCHAR(256),
    street_line VARCHAR(50) NULL,
    city VARCHAR(50) NULL,
    state VARCHAR(50) NULL,
    postal_code VARCHAR(50) NULL,
    country VARCHAR(50) NULL,
    home_phone VARCHAR(20) NULL,
    work_phone VARCHAR(20) NULL,
    fax_number VARCHAR(20) NULL,
    mobile_number VARCHAR(20) NULL,
    created_on DATETIME NOT NULL,
    created_by INTEGER NOT NULL,
     PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE user_details (
    id INTEGER NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    company_name VARCHAR(20) NULL,
    company_profile VARCHAR(50) NULL,
    designation VARCHAR(25) NULL,
    role VARCHAR(50) NULL,
    role_description VARCHAR(255) NULL,
    area_of_practice VARCHAR(255) NULL,
    bank_account_number VARCHAR(50) NULL,
    IFSC_code VARCHAR(20) NULL,
    service_tax_number VARCHAR(20) NULL,
    pan_card_number VARCHAR(20) NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
 PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE cases (
    id INTEGER NOT NULL AUTO_INCREMENT,
    lawyer_id INT NOT NULL,
    client_id INT NOT NULL,
    date_of_allotment DATETIME NOT NULL,
    due_date DATETIME NOT NULL,
    closed_by INT,
    closing_date DATETIME NULL,
    FOREIGN KEY (lawyer_id)
        REFERENCES users (id),
    FOREIGN KEY (client_id)
        REFERENCES users (id),
    FOREIGN KEY (closed_by)
        REFERENCES users (id),
 PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE case_history (
    id INTEGER NOT NULL AUTO_INCREMENT,
    case_id INT NOT NULL,
    hearing_date DATETIME NULL,
    next_hearing_date DATETIME NULL,
    judge_name VARCHAR(25) NOT NULL,
    content VARCHAR(500) NOT NULL,
    FOREIGN KEY (case_id)
        REFERENCES cases (id),
 PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE case_documents (
    id INTEGER NOT NULL AUTO_INCREMENT,
    case_id INT NOT NULL,
    document_name VARCHAR(25) NOT NULL,
    uploaded_by INT NOT NULL,
    uploaded_on DATETIME NOT NULL,
    FOREIGN KEY (case_id)
        REFERENCES cases (id),
 PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE case_transactions (
    id INTEGER NOT NULL AUTO_INCREMENT,
    case_id INT NOT NULL,
    transaction_type_id INT NOT NULL,
    amount float4 NOT NULL,
    submission_date DATETIME NOT NULL,
    submitted_by INT NOT NULL,
    approved_by INT NULL,
    transaction_details VARCHAR(25) NOT NULL,
    FOREIGN KEY (case_id)
        REFERENCES cases (id),
    FOREIGN KEY (submitted_by)
        REFERENCES users (id),
    FOREIGN KEY (approved_by)
        REFERENCES users (id),
 PRIMARY KEY (id)
);

CREATE TABLE comments (
    id INTEGER NOT NULL AUTO_INCREMENT,
    sent_from INT NOT NULL,
    sent_to INT NOT NULL,
    message VARCHAR(50) NOT NULL,
    posted_datetime DATETIME NOT NULL,
    is_admin boolean,
    FOREIGN KEY (sent_from)
        REFERENCES users (id),
    FOREIGN KEY (sent_to)
        REFERENCES users (id),
 PRIMARY KEY (id)
);