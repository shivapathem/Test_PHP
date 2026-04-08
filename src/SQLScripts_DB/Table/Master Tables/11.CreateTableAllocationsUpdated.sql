USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'AllocationsUpdated' )
 BEGIN


	create table AllocationsUpdated	(
	AU_AllocationsUpdID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	AU_AllocationsDutyID	INT,
	AU_AllocationsSPID INT,
	AU_Status	INT,
	AU_UpdatedBy	INT,
	AU_UpdatedDate	DATETIME,
	constraint Con_AllocationsUpdated_UID_FK FOREIGN KEY ( AU_AllocationsDutyID ) references AllocationsDuties(AD_AllocationsDutyID),
	constraint Con_AllocationsUpdated_CrBy_FK FOREIGN KEY ( AU_UpdatedBy ) references UserDetails(UD_UserID),
	constraint Con_AllocationsUpdated_UpdBy_FK FOREIGN KEY ( AU_AllocationsSPID ) references AllocationsScheduledPersons(ASP_AllocationsSPID)
	)

	CREATE NONCLUSTERED INDEX idx_AllocationsUPD_DutyID  ON  AllocationsUpdated
			  (AU_AllocationsDutyID) 
	INCLUDE ( AU_AllocationsSPID )
	
 END	