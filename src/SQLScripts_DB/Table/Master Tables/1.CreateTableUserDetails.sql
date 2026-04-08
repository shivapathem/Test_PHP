USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'UserDetails' )
 BEGIN
 
	create table UserDetails (
	UD_UserID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	UD_EmpNumber	INT,
	UD_StaffNumber	VARCHAR(7),
	UD_NetLogin	VARCHAR(30),
	UD_DisplayName	NVARCHAR(100),
	UD_DisplayFirstName	NVARCHAR(50),
	UD_DisplayLastName	NVARCHAR(50),
	UD_InternalEmail	VARCHAR(132),
	UD_ExternalEmail	VARCHAR(132),
	UD_PersonalPhone	VARCHAR(13),
	UD_AdminNotes	VARCHAR(500),
	UD_FWANotes	VARCHAR(500),
	UD_PHLLeaveAmount	FLOAT,
	UD_TeampayStaffID	INT,
	UD_StartDate	DATE,
	UD_Status	INT,
	UD_CreatedBy	INT,
	UD_CreatedDate	DATETIME,
	UD_UpdatedBy	INT,
	UD_UpdatedDate	DATETIME
	)

	CREATE NONCLUSTERED INDEX idx_UserDetails_UserID  ON  UserDetails
			  (UD_UserID) 	  

	CREATE NONCLUSTERED INDEX idx_UserDetails_NetLogin  ON  UserDetails
			  (UD_NetLogin)
			  
	CREATE NONCLUSTERED INDEX idx_UserDetails_StaffNumber  ON  UserDetails
			  (UD_StaffNumber)		  
		  
 END