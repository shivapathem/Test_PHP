USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'AllocationsJobs_DEL' )
 BEGIN


	create table AllocationsJobs_DEL (
		AJ_AllocateJobID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
		AJ_ORIG_AllocateJobID INT,
		AJ_AllocationsDutyID	INT,
		AJ_MasterJobID	INT,
		AJ_ProgrammeID	INT,
		AJ_Programme	VARCHAR(60),
		AJ_Contact	NVARCHAR(50),
		AJ_Location	NVARCHAR(MAX),
		AJ_JobName	NVARCHAR(100),
		AJ_JobStartTimeSec	INT,
		AJ_JobEndTimeSec	INT,
		AJ_JobStartTimeUTC	DATETIME,
		AJ_JobEndTimeUTC	DATETIME,
		AJ_JobStartTimeLocal	DATETIME,
		AJ_JobEndTimeLocal	DATETIME,
		AJ_JobBGColour	VARCHAR(12),
		AJ_JobFontColour	VARCHAR(12),
		AJ_Comments	VARCHAR(MAX),
		AJ_JobStatus	INT,
		AJ_JobInfo	VARCHAR(1000),
		AJ_IsEditedJobAttention BIT,		
		AJ_CreatedBy	INT,
		AJ_CreatedDate	DATETIME,
		AJ_UpdatedBy	INT,
		AJ_UpdatedDate	DATETIME,
		constraint Con_AllocationsJobs_DEL_CrBy_FK FOREIGN KEY ( AJ_CreatedBy ) references UserDetails(UD_UserID),
		constraint Con_AllocationsJobs_DEL_UpdBy_FK FOREIGN KEY ( AJ_UpdatedBy ) references UserDetails(UD_UserID),
		constraint Con_AllocationsJobs_DEL_AD_FK FOREIGN KEY ( AJ_AllocationsDutyID ) references AllocationsDuties_DEL(AD_AllocationsDutyID),
		constraint Con_AllocationsJobs_DEL_MJ_FK FOREIGN KEY ( AJ_MasterJobID ) references Masterjobs(MasterJobID),
		)

	CREATE NONCLUSTERED INDEX idx_AllocationsJobs_DEL_DutyID  ON  AllocationsJobs_DEL
			  (AJ_AllocationsDutyID) 
			  
 END			  

