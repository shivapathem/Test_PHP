USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsDay_Test]    Script Date: 29/09/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_ReadAllocationsDay]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_ReadAllocationsDay_Test]
@WeekNumber varchar(100),
@iDay        varchar(100),
@SchedulingTeamId varchar(100),
@SortOrder  INT,
@StartTime varchar(100)


AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @conditionstring Varchar(max)
	DECLARE @sql varchar(max)
	DECLARE @sortsql Varchar(max)

	if (@SortOrder = null or @SortOrder = '''')
	SET @SortOrder = 0;

	SET @conditionstring = ''''


	IF EXISTS 
		(Select a.id
		from
		Allocations a
		LEFT JOIN 
		Allocations_edit ae
		ON ae.AllocationID = a.ID
		WHERE a.SchedulingTeamId = @SchedulingTeamId
		and a.WeekNumber = @WeekNumber
		and a.iDay = @iDay)
	SET @conditionstring  = '' AND a.StartTime >=''''''+ @StartTime+''''''  ''
	ELSE
	SET @conditionstring  = '' AND ae.StartTime >=''''''+ @StartTime+''''''  ''

	
 
	IF(@SortOrder=0)
		SET @sortsql = '' ORDER BY  StartTime, DutyName ''
	ELSE IF(@SortOrder=1)
		SET @sortsql = '' ORDER BY DutyName, StartTime ''
	ELSE IF(@SortOrder=2)
		SET @sortsql = '' ORDER BY a.schedulingTeamId, spt.SortCode, StaffDetails.Surname, sd.Forename ''
	ELSE
		SET @sortsql = ''''

	

	IF @conditionstring != ''''
		BEGIN
		SET @sql = ''SELECT sp.DisplayName,sp.StaffDetailsID,sd.StaffID,a.StaffNumber,a.SchedulingPersonID, a.WeekNumber,sd.Forename + '''''''' + sd.Surname AS FullName, isnull(sd.InternalEmail, '''''''') AS StaffInternalEmail, isnull(sd.ExternalEmail, '''''''') AS StaffExernalEmail, sd.StaffNumber, spt.SortCode, a.schedulingTeamId,
   a.StartDate,a.EndDate,sg.active, sg.inBuilding, sg.starttime AS SignInStartTime, sg.endtime AS SignInEndTime,
   CASE WHEN ae.ID IS NULL THEN a.ID ELSE ae.AllocationID END AS DutyID, 
  CASE WHEN ae.ID IS NULL THEN 0 ELSE 1 END AS iscopy, 
  CASE WHEN ae.ID IS NULL THEN 0 ELSE ae.edited END AS isedited, 
  CASE WHEN ae.ID IS NULL THEN a.DutyName ELSE ae.DutyName END AS DutyName, 
  CASE WHEN ae.ID IS NULL THEN a.StartTime ELSE ae.StartTime END AS StartTime, 
  CASE WHEN ae.ID IS NULL THEN a.EndTime ELSE ae.EndTime END AS EndTime, 
  CASE WHEN ae.ID IS NULL THEN a.Duration ELSE ae.Duration END AS Duration, 
  CASE WHEN ae.ID IS NULL THEN 0 ELSE ae.InternalEdited END AS InternalEdited, 
  CASE WHEN ae.ID IS NULL THEN 0 ELSE ae.editable END AS editable, 
  CASE WHEN ae.ID IS NULL THEN 0 ELSE ae.edited END AS Edited, 
  CASE WHEN ae.ID IS NULL THEN CAST(a.BackColour AS nvarchar) ELSE CAST(ae.BackColour AS nvarchar) END AS AllocBackColour, 
  CASE WHEN ae.ID IS NULL THEN CAST(a.FontColour AS nvarchar) ELSE CAST(ae.FontColour AS nvarchar) END AS AllocFontColour, 
  CASE WHEN ae.ID IS NULL THEN CASE WHEN a.DutyComments IS NULL THEN 0 ELSE DataLength(a.DutyComments) END 
  ELSE CASE WHEN ae.DutyComments IS NULL THEN 0 ELSE DataLength(ae.DutyComments) END END AS DutyComments, 
  CASE WHEN ae.ID IS NULL THEN CASE WHEN a.PersonComments IS NULL THEN 0 ELSE DataLength(a.PersonComments) END 
  ELSE CASE WHEN ae.PersonComments IS NULL THEN 0 ELSE DataLength(ae.PersonComments) END END AS PersonComments, 
  CASE WHEN ae.ID IS NULL THEN aj.ID ELSE aje.ID END AS JobID, 
  CASE WHEN ae.ID IS NULL THEN aj.SchedulingPersonID ELSE aje.SchedulingPersonID END AS JobSchedulingPersonID, 
  CASE WHEN ae.ID IS NULL THEN aj.StartTime ELSE aje.StartTime END AS JobStartTime, 
  CASE WHEN ae.ID IS NULL THEN aj.EndTime ELSE aje.EndTime END AS JobEndTime, 
  CASE WHEN ae.ID IS NULL THEN aj.JobName ELSE aje.JobName END AS JobName, 
  CASE WHEN ae.ID IS NULL THEN aj.Programme ELSE aje.Programme END AS Programme, 
  CASE WHEN ae.ID IS NULL THEN 0 ELSE aj.Edited END AS JobEdited, 
  CASE WHEN ae.ID IS NULL THEN CAST(aj.JobBackColour AS nvarchar) 
  ELSE CAST(aje.JobBackColour AS nvarchar) END AS JobBackColour, 
  CASE WHEN ae.ID IS NULL THEN CAST(aj.JobFontColour AS nvarchar) ELSE CAST(aje.JobFontColour AS nvarchar) 
  END AS JobFontColour, 
  CASE WHEN ae.ID IS NULL THEN aj.Comments ELSE aje.Comments END AS JobComments,
  CASE WHEN ae.ID IS NULL THEN 0 ELSE ae.IsAttention END AS IsAttention
   
 
FROM   ScheduledPersonTeam_LINK  spt INNER JOIN
ScheduledPeople sp ON  spt.ScheduledPersonID=sp.ScheduledPersonID
AND spt.TeamID=''+@SchedulingTeamId+''
LEFT JOIN StaffDetails sd ON sp.StaffDetailsID=sd.StaffID
 RIGHT OUTER JOIN
  Allocations a LEFT OUTER JOIN
  Allocations_edit ae ON a.ID=ae.AllocationID AND a.iDay = ae.iDay AND a.WeekNumber = ae.WeekNumber AND 
  a.SchedulingPersonID = ae.SchedulingPersonID AND a.schedulingTeamId = ae.schedulingTeamId ON 
  sp.ScheduledPersonID = a.SchedulingPersonID 
  LEFT OUTER JOIN 
  Allocations_jobs aj ON  a.ID = aj.AllocationID AND a.schedulingTeamId = aj.schedulingTeamId LEFT OUTER JOIN
  Allocation_jobs_edit aje ON ae.schedulingTeamId = aje.schedulingTeamId AND 
  ae.AllocationID = aje.AllocationID 
  LEFT OUTER JOIN
  signin sg ON a.StaffNumber = sg.staffnumber AND a.WeekNumber = sg.iWeek AND a.iDay = sg.iDay
	WHERE         (a.schedulingTeamId = ''+@SchedulingTeamId+'') 
	AND (a.SchedulingPersonID is not null)
	AND           (a.WeekNumber = ''+@WeekNumber+'') 
	AND           (a.iDay = ''+@iDay+'')''
	SET @sql = @sql + @conditionstring + @sortsql

	
	exec (@sql)
	END
	ELSE
	   BEGIN
	      
		  RETURN 0
		END
	
END
'
EXEC dbo.sp_executesql @strSQL

GO
