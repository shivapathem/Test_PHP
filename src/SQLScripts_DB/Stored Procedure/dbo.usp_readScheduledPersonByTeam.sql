USE [ALLOCATE7]
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_readScheduledPersonByTeam]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_readScheduledPersonByTeam] 
@Team VARCHAR(50),
@date varchar(25),
@filterCond VARCHAR(MAX),
@teamFilterCond VARCHAR(MAX),
@scheduledPersonId VARCHAR(50)

AS
BEGIN
	SET NOCOUNT ON;

	DECLARE @sql VARCHAR(MAX),
			@sqlWhereCond VARCHAR(MAX)

	IF(@teamFilterCond != '''')
		BEGIN
			SET @teamFilterCond = REPLACE(@teamFilterCond,''a.SchedulingTeamId'',''stl.TeamID'')
			SET @teamFilterCond = REPLACE(@teamFilterCond,''spl.TeamId'',''stl.TeamID'')
		END
	ELSE
		BEGIN
			SET @teamFilterCond = '' stl.TeamID IN(''+@Team+'') ''
		END

	IF(@filterCond != '''')
		BEGIN
			SET @filterCond = '' AND (''+@filterCond+'')''
			SET @sqlWhereCond = ''''
		END
	ELSE
		BEGIN
			SET @filterCond = ''''
			IF(@scheduledPersonId != '''' OR @scheduledPersonId != 0)
					SET @sqlWhereCond = '' AND sp.ScheduledPersonID = ''+@scheduledPersonId+'' ''
				ELSE
					SET @sqlWhereCond = ''''
		END
	BEGIN
		SET @sql = ''SELECT stl.ScheduledPersonID,stl.TeamID,stl.IsHomeTeam,stl.SortCode,stl.BackgroundColour as peoplebg,stl.fontcolour as peoplefontcolor,stl.IsAvailable,stl.StartDate,stl.EndDate,stl.IsActive,ISNULL(stl.fontcolour,''''#000000'''') AS StaffTextColour,ISNULL(stl.BackgroundColour,''''#cccccc'''') AS StaffBackColour,
		CASE WHEN (sp.DisplayName IS NULL) THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '''''''')
THEN (sd.Forename + '''''''' + sd.Surname) ELSE (sd.PreferredForename + '''''''' + sd.Surname) END
ELSE sp.DisplayName END AS DisplayName ,
		sd.NetLogin as Login,sd.StaffNumber,sd.InternalEmail as email
			FROM ScheduledPersonTeam_LINK as stl (NOLOCK) 
			INNER JOIN ScheduledPeople as sp (NOLOCK) on sp.ScheduledPersonID =stl.ScheduledPersonID
			LEFT JOIN StaffDetails as sd (NOLOCK)  on sd.StaffID =sp.StaffDetailsID
			WHERE ''+@teamFilterCond+'' AND (stl.StartDate <=convert(date,''''''+@date+'''''') AND stl.EndDate >=convert(date,''''''+@date+'''''') OR EndDate is NULL) 
			AND stl.IsActive=1 AND scheduledType=1
			''+@filterCond+'' ''+@sqlWhereCond+''
			ORDER BY DisplayName ASC''
		exec(@sql)
		
	END
END'
EXEC dbo.sp_executesql @strSQL

GO