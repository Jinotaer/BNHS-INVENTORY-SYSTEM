-- Create the database
CREATE DATABASE IF NOT EXISTS bnhs_inventory;
USE bnhs_inventory;

-- Create entity table
CREATE TABLE entities (
    entity_id INT PRIMARY KEY AUTO_INCREMENT,
    entity_name VARCHAR(100) NOT NULL,
    fund_cluster VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create suppliers table
CREATE TABLE suppliers (
    supplier_id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(100) NOT NULL,
    contact_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create items table
CREATE TABLE items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    stock_no VARCHAR(50) UNIQUE,
    item_description TEXT NOT NULL,
    unit VARCHAR(20) NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    estimated_useful_life INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

    -- Create inspection_acceptance_reports table
    CREATE TABLE inspection_acceptance_reports (
        iar_id INT PRIMARY KEY AUTO_INCREMENT,
        entity_id INT NOT NULL,
        supplier_id INT NOT NULL,
        iar_no VARCHAR(50) NOT NULL UNIQUE,
        po_no_date VARCHAR(100),
        req_office VARCHAR(100),
        responsibility_center VARCHAR(100),
        iar_date DATE NOT NULL,
        invoice_no_date VARCHAR(100),
        receiver_name VARCHAR(100) NOT NULL,
        teacher_id VARCHAR(50),
        position VARCHAR(100),
        date_inspected DATE,
        inspectors TEXT,
        barangay_councilor VARCHAR(100),
        pta_observer VARCHAR(100),
        date_received DATE,
        property_custodian VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (entity_id) REFERENCES entities(entity_id),
        FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
    );

    -- Create iar_items table (for items in IAR)
    CREATE TABLE iar_items (
        iar_item_id INT PRIMARY KEY AUTO_INCREMENT,
        iar_id INT NOT NULL,
        item_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        remarks TEXT,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (iar_id) REFERENCES inspection_acceptance_reports(iar_id),
        FOREIGN KEY (item_id) REFERENCES items(item_id)
    );

-- Create property_acknowledgment_receipts table
CREATE TABLE property_acknowledgment_receipts (
    par_id INT PRIMARY KEY AUTO_INCREMENT,
    entity_id INT NOT NULL,
    par_no VARCHAR(50) NOT NULL UNIQUE,
    date_acquired DATE NOT NULL,
    end_user_name VARCHAR(100) NOT NULL,
    receiver_position VARCHAR(100),
    receiver_date DATE,
    custodian_name VARCHAR(100) NOT NULL,
    custodian_position VARCHAR(100),
    custodian_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES entities(entity_id)
);

-- Create par_items table (for items in PAR)
CREATE TABLE par_items (
    par_item_id INT PRIMARY KEY AUTO_INCREMENT,
    par_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    property_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (par_id) REFERENCES property_acknowledgment_receipts(par_id),
    FOREIGN KEY (item_id) REFERENCES items(item_id)
);

-- Create requisition_and_issue_slips table
CREATE TABLE requisition_and_issue_slips (
    ris_id INT PRIMARY KEY AUTO_INCREMENT,
    entity_id INT NOT NULL,
    division VARCHAR(100),
    office VARCHAR(100),
    responsibility_code VARCHAR(50),
    ris_no VARCHAR(50) NOT NULL UNIQUE,
    purpose TEXT,
    requested_by_name VARCHAR(100) NOT NULL,
    requested_by_designation VARCHAR(100),
    requested_by_date DATE,
    approved_by_name VARCHAR(100),
    approved_by_designation VARCHAR(100),
    approved_by_date DATE,
    issued_by_name VARCHAR(100),
    issued_by_designation VARCHAR(100),
    issued_by_date DATE,
    received_by_name VARCHAR(100),
    received_by_designation VARCHAR(100),
    received_by_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES entities(entity_id)
);

-- Create ris_items table (for items in RIS)
CREATE TABLE ris_items (
    ris_item_id INT PRIMARY KEY AUTO_INCREMENT,
    ris_id INT NOT NULL,
    item_id INT NOT NULL,
    requested_qty INT NOT NULL,
    stock_available INT,
    issued_qty INT,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ris_id) REFERENCES requisition_and_issue_slips(ris_id),
    FOREIGN KEY (item_id) REFERENCES items(item_id)
);

-- Create inventory_custodian_slips table
CREATE TABLE inventory_custodian_slips (
    ics_id INT PRIMARY KEY AUTO_INCREMENT,
    entity_id INT NOT NULL,
    ics_no VARCHAR(50) NOT NULL UNIQUE,
    end_user_name VARCHAR(100) NOT NULL,
    end_user_position VARCHAR(100),
    end_user_date DATE,
    custodian_name VARCHAR(100) NOT NULL,
    custodian_position VARCHAR(100),
    custodian_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES entities(entity_id)
);

-- Create ics_items table (for items in ICS)
CREATE TABLE ics_items (
    ics_item_id INT PRIMARY KEY AUTO_INCREMENT,
    ics_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    inventory_item_no VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ics_id) REFERENCES inventory_custodian_slips(ics_id),
    FOREIGN KEY (item_id) REFERENCES items(item_id)
);

-- Create indexes for frequently queried fields
CREATE INDEX idx_iar_no ON inspection_acceptance_reports(iar_no);
CREATE INDEX idx_par_no ON property_acknowledgment_receipts(par_no);
CREATE INDEX idx_ris_no ON requisition_and_issue_slips(ris_no);
CREATE INDEX idx_ics_no ON inventory_custodian_slips(ics_no);
CREATE INDEX idx_stock_no ON items(stock_no);
CREATE INDEX idx_property_number ON par_items(property_number);
CREATE INDEX idx_inventory_item_no ON ics_items(inventory_item_no); 