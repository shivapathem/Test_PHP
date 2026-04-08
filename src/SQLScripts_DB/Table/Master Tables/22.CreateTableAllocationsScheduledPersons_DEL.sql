USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'AllocationsScheduledPersons_DEL' )
 BEGIN


	create table AllocationsScheduledPersons_DEL (
		ASP_AllocationsSPID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
		ASP_ORIG_AllocationsSPID INT,
		ASP_AllocationsID	INT,
		ASP_AllocationsDutyID	INT,
		ASP_SchedulingPersonID	INT,
		ASP_iDay	INT,
		ASP_SortCode	NVARCHAR(30),
		ASP_LeaveStatus	INT,
		ASP_LeaveType INT,
		ASP_DutyDate	DATE,
		ASP_WIADStatus	INT,
		ASP_MarkedOverTime	BIT,
		ASP_OverTimeHours	INT,
		ASP_DutyTeamID	INT,
		ASP_SigninStartTime	INT,
		ASP_SigninEndTime	INT,
		ASP_SigninStatus	INT,
		ASP_SigninINBuilding	BIT,
		ASP_EDPStatus	INT,
		ASP_Comments	VARCHAR(MAX),
		ASP_LeaveStartTimeSec	INT,
		ASP_LeaveEndTimeSec	INT,
		ASP_LeaveStartTimeLocal	DATETIME,
		ASP_LeaveEndTimeLocal	DATETIME,
		ASP_LeaveDuration		INT,
		ASP_UnderElevenBreakStatus	INT,
		ASP_CalculatedUnderElevenHrs	INT,
		ASP_OverrideUnderElevenHrs	INT,
		ASP_UnderElevenComments VARCHAR(MAX),
		ASP_OverTwelveStatus INT, 
		ASP_OverTwelveHrs INT,
		ASP_IsOverseasOverTwelve BIT,		
		ASP_RequestsStatus INT,
		ASP_RequestsCount INT,
		ASP_LockRequestsStatus INT,
		ASP_ChargingStatus INT,
		ASP_CreatedBy	INT,
		ASP_CreatedDate	DATETIME,
		ASP_UpdatedBy	INT,
		ASP_UpdatedDate	DATETIME ,
		constraint Con_AllocationsSP_DEL_CrBy_FK FOREIGN KEY ( ASP_CreatedBy ) references UserDetails(UD_UserID),
		constraint Con_AllocationsSP_DEL_UpdBy_FK FOREIGN KEY ( ASP_UpdatedBy ) references UserDetails(UD_UserID),
		constraint Con_AllocationsSP_DEL__SP_FK FOREIGN KEY ( ASP_SchedulingPersonID ) references UserDetails(UD_UserID),
		constraint Con_AllocationsSP_DEL_AL_FK FOREIGN KEY ( ASP_AllocationsID ) references Allocations_DEL(AL_AllocationsID),
		constraint Con_AllocationsSP_DEL_MD_FK FOREIGN KEY ( ASP_AllocationsDutyID ) references AllocationsDuties_DEL(AD_AllocationsDutyID) )

	CREATE NONCLUSTERED INDEX idx_AllocationsSP_DEL_AllocationsID  ON  AllocationsScheduledPersons_DEL
			  (ASP_AllocationsID) 
	INCLUDE ( ASP_iDay )		  

	CREATE NONCLUSTERED INDEX idx_AllocationsSP_DEL_DutyDate  ON  AllocationsScheduledPersons_DEL
			  ( ASP_DutyDate)
			  
 END			  