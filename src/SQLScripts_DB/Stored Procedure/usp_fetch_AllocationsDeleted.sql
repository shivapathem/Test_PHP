USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_AllocationsDeleted]    Script Date: 10/07/2025 13:05:42 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER    PROCEDURE [dbo].[usp_fetch_AllocationsDeleted]
@intDay INT,
@intWeek INT,
@teamId INT

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	
		SELECT AD_AllocationsDutyID        DutyID,
			   AD_DutyName DutyName,
			   AD_Duration Duration,
			   AD_StartTimeSec AS DutyStartTime,
			   AD_EndTimeSec   AS DutyEndTime,
			   AJ_AllocateJobID        JobID,
			   AJ_AllocationsDutyID AllocationID,
			   AJ_ProgrammeID ProgrammeId,
			   PS.Programme,
			   AJ_JobName as JobName,
			   AJ_JobStartTimeSec AS JobStartTime,
			   AJ_JobEndTimeSec   AS JobEndTime
		FROM   Allocations AL
		INNER JOIN AllocationsDuties (NoLock) AS AD on AL_AllocationsID = AD_AllocationsID
		LEFT JOIN AllocationsJobs (NoLock) AS AJ ON AJ.AJ_AllocationsDutyID = AD_AllocationsDutyID
		LEFT JOIN Programmes AS PS ON AJ_ProgrammeID = PS.ID
		WHERE AL_WeekNumber = @intWeek
		  AND AD_iDay = @intDay
		  AND AL_SchedulingTeamID = @teamId
		  AND AD_DutyStatus = 9
		UNION
		SELECT AD_AllocationsDutyID        DutyID,
			   AD_DutyName DutyName,
			   AD_Duration Duration,
			   AD_StartTimeSec AS DutyStartTime,
			   AD_EndTimeSec   AS DutyEndTime,
			   AJ_AllocateJobID        JobID,
			   AJ_AllocationsDutyID AllocationID,
			   AJ_ProgrammeID ProgrammeId,
			   PS.Programme,
			   AJ_JobName as JobName,
			   AJ_JobStartTimeSec AS JobStartTime,
			   AJ_JobEndTimeSec   AS JobEndTime
		FROM   Allocations AL
		INNER JOIN AllocationsDuties (NoLock) AS AD on AL_AllocationsID = AD_AllocationsID
		LEFT JOIN AllocationsJobs (NoLock) AS AJ ON AJ.AJ_AllocationsDutyID = AD_AllocationsDutyID
		LEFT JOIN Programmes AS PS ON AJ_ProgrammeID = PS.ID
		WHERE AL_WeekNumber = @intWeek
		  AND AD_iDay = @intDay
		  AND AL_SchedulingTeamID = @teamId
		  AND AD_DutyType = 10
		  AND AJ_JobStatus = 9
		
 END