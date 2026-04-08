 USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadIndvidualAllocationsSignIN]    Script Date: 29/09/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_ReadIndvidualAllocationsSignIN]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_get_ReadIndvidualAllocationsSignIN]
@WeekNumber varchar(100),
@iDay        varchar(100),
@SchedulingPersonId  INT,
@SchedulingTeamId varchar(100)


AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	SELECT ISNULL(sd.Forename + '' '' + sd.Surname, sp.DisplayName)  AS FullName,a.SchedulingPersonID,sd.StaffNumber as StaffNumber,spl.SortCode, signin.active, signin.inBuilding, signin.starttime AS SignInStartTime, 
	signin.endtime AS SignInEndTime, a.schedulingTeamId,spl.fontcolour as StaffTextColour,
	a.ID  AS DutyID,0  AS iscopy,0  AS isedited,a.DutyName AS DutyName,a.StartTime  AS StartTime,a.StartDate as DutyStartDate,
	a.EndTime AS EndTime,a.Duration  AS Duration,  0  AS InternalEdited,  0  AS editable,   0  AS Edited, 
	CAST(a.BackColour AS nvarchar)  AS AllocBackColour,CAST(a.FontColour AS nvarchar)  AS AllocFontColour,
	a.DutyComments AS DutyComments,a.PersonComments AS PersonComments,aj.ID  AS JobID,
	aj.StartTime  AS JobStartTime,aj.EndTime  AS JobEndTime,aj.JobName  AS JobName, 
	aj.Programme  AS Programme, aj.StaffNumber  AS JobStaffNumber,0  AS JobEdited, 
	CAST(aj.JobBackColour AS nvarchar) AS JobBackColour,CAST(aj.JobFontColour AS nvarchar) AS JobFontColour,
	aj.Comments  AS JobComments 
	FROM dbo.Allocations as a
	INNER JOIN ScheduledPeople as sp (NOLOCK) on sp.ScheduledPersonID = a.SchedulingPersonID
	INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on sp.ScheduledPersonID = spl.ScheduledPersonID
	and  spl.TeamID=a.schedulingTeamId
	LEFT JOIN StaffDetails sd (NOLOCK) on sd.StaffID = sp.StaffDetailsID
	LEFT JOIN Allocations_jobs aj ON a.ID = aj.AllocationID
	LEFT OUTER JOIN Signin ON a.SchedulingPersonID = signin.SchedulingPersonID AND a.WeekNumber = signin.iWeek 
	AND a.iDay = signin.iDay
	WHERE (a.SchedulingPersonID = @SchedulingPersonId) AND  (a.WeekNumber = @WeekNumber) AND (a.iDay = @iDay)
	and a.IsActive=1  ORDER BY StartTime

   END
   '
EXEC dbo.sp_executesql @strSQL

GO