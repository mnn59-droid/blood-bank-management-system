CREATE DATABASE IF NOT EXISTS bloodbank_db;
USE bloodbank_db;

CREATE TABLE Admin (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Donor (
    DonorID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Gender VARCHAR(10) NOT NULL,
    DOB DATE NOT NULL,
    BloodGroup VARCHAR(5) NOT NULL,
    Phone VARCHAR(20) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Address TEXT,
    Password VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'Available',
    LastDonationDate DATE DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Hospital (
    HospitalID INT AUTO_INCREMENT PRIMARY KEY,
    HospitalName VARCHAR(120) NOT NULL,
    ContactPerson VARCHAR(100) DEFAULT NULL,
    Phone VARCHAR(20) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Address TEXT,
    Password VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE BloodStock (
    StockID INT AUTO_INCREMENT PRIMARY KEY,
    BloodGroup VARCHAR(5) NOT NULL UNIQUE,
    UnitsAvailable INT NOT NULL DEFAULT 0,
    LastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Donation (
    DonationID INT AUTO_INCREMENT PRIMARY KEY,
    DonorID INT NOT NULL,
    BloodGroup VARCHAR(5) NOT NULL,
    UnitsDonated INT NOT NULL,
    DonationDate DATE NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_donation_donor
        FOREIGN KEY (DonorID) REFERENCES Donor(DonorID)
        ON DELETE CASCADE
);

CREATE TABLE BloodRequest (
    RequestID INT AUTO_INCREMENT PRIMARY KEY,
    HospitalID INT NOT NULL,
    BloodGroup VARCHAR(5) NOT NULL,
    UnitsRequested INT NOT NULL,
    RequestDate DATE NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bloodrequest_hospital
        FOREIGN KEY (HospitalID) REFERENCES Hospital(HospitalID)
        ON DELETE CASCADE
);

CREATE TABLE DonationRequest (
    RequestID INT AUTO_INCREMENT PRIMARY KEY,
    DonorID INT NOT NULL,
    BloodGroup VARCHAR(5) NOT NULL,
    UnitsRequested INT NOT NULL,
    RequestDate DATE NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_donationrequest_donor
        FOREIGN KEY (DonorID) REFERENCES Donor(DonorID)
        ON DELETE CASCADE
);

INSERT INTO BloodStock (BloodGroup, UnitsAvailable) VALUES
('A+', 0),
('A-', 0),
('B+', 0),
('B-', 0),
('AB+', 0),
('AB-', 0),
('O+', 0),
('O-', 0)
ON DUPLICATE KEY UPDATE BloodGroup = VALUES(BloodGroup);

CREATE INDEX idx_donor_bloodgroup ON Donor(BloodGroup);
CREATE INDEX idx_donation_date ON Donation(DonationDate);
CREATE INDEX idx_bloodrequest_status ON BloodRequest(Status);
CREATE INDEX idx_donationrequest_status ON DonationRequest(Status);
