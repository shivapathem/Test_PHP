USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetPublishallocationBySchedulledPersonID]    Script Date: 31/05/2022 18:03:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_GetPublishallocationBySchedulledPersonID]
	-- Add the parameters for the stored procedure here
	@strScheduledPerson varchar(100),
	@$intWeekNumber varchar(12) 

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	Declare @strQuery VARCHAR(MAX);

    -- Insert statements for procedure here
	SET @strQuery ='SELECT ap.iDay,  ap.DutyName  AS DutyName, 
    schedulingTeams.schedulingTeamName,  LOWER(ISNULL(Staff.NetLogin, '''')) AS Login
    FROM  Allocations_Publish  ap (Nolock)
    INNER JOIN schedulingTeams (Nolock) ON ap.SchedulingTeamId = schedulingTeams.schedulingTeamId ANd schedulingTeams.isActive=1
    INNER JOIN ScheduledPeople as sp (Nolock) ON ap.SchedulingPersonID = sp.ScheduledPersonID 
    LEFT JOIN  StaffDetails as Staff (Nolock) ON sp.StaffDetailsID = Staff.StaffID 
    WHERE (ap.WeekNumber = '''+@$intWeekNumber+''')
    AND (ap.SchedulingPersonID IN ('''+@strScheduledPerson+'''))
    GROUP BY ap.iDay,schedulingTeams.schedulingTeamName, ISNULL(Staff.NetLogin, ''''), ap.DutyName 
    HAVING (ISNULL(Staff.NetLogin, '''') <> '''')
    ORDER BY ap.iDay';
	exec(@strQuery);
	
END
