USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'UserConfigs' )
 BEGIN

	create table UserConfigs (
	UC_UserConfigID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	UC_UserID	INT,
	UC_EFT	DECIMAL(4,3),
	UC_CostCode	VARCHAR(10),
	UC_AccGroupID	INT,
	UC_AccGroup	VARCHAR(5),
	UC_PaymentTypeID	INT,
	UC_ManualEDP	BIT,
	UC_PartTimeEDP	DECIMAL(6,2),
	UC_Status	INT,
	UC_StartDate	DATE,
	UC_EndDate	DATE,
	UC_CreatedBy	INT,
	UC_CreatedDate	DATETIME,
	UC_UpdatedBy	INT,
	UC_UpdatedDate	DATETIME 
	constraint Con_UserConfigs_UID_FK FOREIGN KEY ( UC_UserID ) references UserDetails(UD_UserID),
	constraint Con_UserConfigs_CrBy_FK FOREIGN KEY ( UC_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_UserConfigs_UpdBy_FK FOREIGN KEY ( UC_UpdatedBy ) references UserDetails(UD_UserID)
	)

	CREATE NONCLUSTERED INDEX idx_UserConfigs_UserID  ON  UserConfigs
			  (UC_UserID)
	INCLUDE (	UC_Status,	 UC_StartDate, UC_EndDate)

 END

