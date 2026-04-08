USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_PublishedAllocations]    Script Date: 16/03/2022 14:33:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_PublishedAllocations]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_PublishedAllocations]
@StartWeek varchar(100),
@EndWeek varchar(100),
@SchedulingPersonId  INT,
@NetLogin varchar(100)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


	SELECT   sd.StaffID AS StaffID, ISNULL(sd.Forename + '' '' + sd.Surname, sp.DisplayName)  AS FullName,sd.StaffNumber, A.schedulingTeamId, sd.InternalEmail As Email, spl.SortCode, 
	A.WeekNumber, A.iDay, A.ID AS DutyID, sp.ScheduledPersonID,
	0  AS iscopy,0  AS isedited, A.MarkedOvertime,A.MarkedSickness,C.ChargingId, A.ActingGrade, A.MarkedPTExtraDay, A.MarkedCompLeave, 
	A.DutyName AS DutyName,
	A.DutyDate As DutyDate,
	A.StartTime  AS StartTime, 
	A.EndTime AS EndTime, 
	A.Duration  AS Duration, 
	A.dutyColorId AS dutyColorId,
	CAST(A.BackColour AS nvarchar)  AS AllocBackColour,
	CAST(A.FontColour AS nvarchar)  AS AllocFontColour,
	A.DutyComments AS DutyComments,A.PersonComments AS PersonComments
	from Allocations A (NOLOCK)
	INNER JOIN ScheduledPeople as sp (NOLOCK) on sp.ScheduledPersonID = A.SchedulingPersonID
	INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on sp.ScheduledPersonID = spl.ScheduledPersonID
	and  spl.TeamID=A.schedulingTeamId
	LEFT JOIN StaffDetails sd (NOLOCK) on sd.StaffID = sp.StaffDetailsID
	LEFT JOIN ChargingDutyMapping_Link C (NOLOCK) ON C.AllocationId=A.ID
	WHERE (A.WeekNumber >= @StartWeek) AND (A.WeekNumber <= @EndWeek) AND 
	A.SchedulingPersonID = @SchedulingPersonId
	AND (sd.NetLogin = @NetLogin)
	 And spl.isActive = 1  
	ORDER BY  sd.Surname, sd.Forename, A.WeekNumber, A.iDay	
			
 END
 '
EXEC dbo.sp_executesql @strSQL

GO
