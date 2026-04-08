USE [ALLOCATE7]
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_GetrotainformationBySchedulledPersonID]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_GetrotainformationBySchedulledPersonID]
	-- Add the parameters for the stored procedure here
	@strScheduledPerson varchar(100)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	Declare @strQuery VARCHAR(MAX);

    -- Select statements for procedure here
	SET @strQuery =''SELECT LOWER(sd.NetLogin ) as Login, CASE WHEN (sp.DisplayName IS NULL) THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '''''''')
                THEN (sd.Forename + '''''''' + sd.Surname) ELSE (sd.PreferredForename + '''''''' + sd.Surname)    END
            ELSE sp.DisplayFirstName + '''''''' + sp.DisplayLastName END AS FullName, er.RotaWeek as iWeek, er.DOTW, er.DutyName, er.WeeksInRota, spl.TeamID, sd.Surname, sd.Forename, spl.rota,
	er.AssignmentStartWeek as StartWeek,er.SchedulingPersonID , spl.SortCode
	FROM  ScheduledPersonTeam_LINK (nolock) as spl 
    INNER JOIN ScheduledPeople (nolock)  as sp on sp.ScheduledPersonID = spl.ScheduledPersonID
    INNER JOIN Exported_rota (nolock) as er on er.SchedulingPersonID=spl.ScheduledPersonID AND spl.TeamID = er.SchedulingTeamId
	LEFT JOIN StaffDetails  (nolock) as sd on sd.StaffID = sp.StaffDetailsID
    LEFT OUTER JOIN StaffConfig (nolock) sc ON sd.StaffID = sc.StaffID 
    AND spl.TeamID = er.SchedulingTeamId
    WHERE (er.SchedulingPersonID IN (''''''+@strScheduledPerson+'''''')) OR (NOT (sd.NetLogin IS NULL))
    ORDER By sd.Surname, sd.Forename'';
	exec(@strQuery);
END
'
EXEC dbo.sp_executesql @strSQL

GO
