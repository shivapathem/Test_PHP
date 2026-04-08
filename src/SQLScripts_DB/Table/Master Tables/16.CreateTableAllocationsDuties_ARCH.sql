USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'AllocationsDuties_ARCH' )
 BEGIN


	create table AllocationsDuties_ARCH (
	AD_AllocationsDutyID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	AD_AllocationsID	INT,
	AD_DutyName	NVARCHAR(100),
	AD_Duration	INT,
	AD_iDay	INT,
	AD_StartTimeSec	INT,
	AD_EndTimeSec	INT,
	AD_DutyBreakTime	INT,
	AD_DutyDate	DATE,
	AD_DutyStartTimeUTC	DATETIME,
	AD_DutyEndTimeUTC	DATETIME,
	AD_DutyStartTimeLocal	DATETIME,
	AD_DutyEndTimeLocal	DATETIME,
	AD_MasterDutyID	INT,
	AD_DutyType	INT,
	AD_DutyStatus	INT,
	AD_DutyColourID	INT,
	AD_Comments	VARCHAR(MAX),
	AD_isAttention	INT,
	AD_isRequest	BIT,
	AD_DutyProgramID1	INT,
	AD_DutyProgramID2	INT,
	AD_DutyProgramID3	INT,
	AD_DutyProgramID4	INT,
	AD_DutyProgramID5	INT,
	AD_DutyProgramID6	INT,
	AD_PlannedDuration	INT,
	AD_PlannedDutyBreakTime	INT,
	AD_IsNeedCovering	BIT,
	AD_IsOverrideOver12	BIT,
	AD_IsDutyEdited	BIT,
	AD_IsEditedDutyAttention BIT,	
	AD_CreatedBy	INT,
	AD_CreatedDate	DATETIME,
	AD_UpdatedBy	INT,
	AD_UpdatedDate	DATETIME,
	constraint Con_AllocationsDuties_ARCH_CrBy_FK FOREIGN KEY ( AD_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_AllocationsDuties_ARCH_UpdBy_FK FOREIGN KEY ( AD_UpdatedBy ) references UserDetails(UD_UserID),
	constraint Con_AllocationsDuties_ARCH_AL_FK FOREIGN KEY ( AD_AllocationsID ) references Allocations_ARCH(AL_AllocationsID),
	constraint Con_AllocationsDuties_ARCH_MD_FK FOREIGN KEY ( AD_MasterDutyID ) references MasterDuties(MasterDutyID) )

	CREATE NONCLUSTERED INDEX idx_AllocationsDuties_ARCH_AllocationsID  ON  AllocationsDuties_ARCH
			  (AD_AllocationsID) 
	INCLUDE ( AD_iDay )		  

	CREATE NONCLUSTERED INDEX idx_AllocationsDuties_ARCH_DutyDate  ON  AllocationsDuties_ARCH
			  ( AD_DutyDate)
			  
 END			  