USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_BreaksByTeam]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_BreaksByTeam]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_BreaksByTeam] 
		-- Add the parameters for the stored procedure here
	@intTeamID int
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statementsss.
	SET NOCOUNT ON;

   		

SELECT           sd.StaffNumber, ISNULL(BreaksTableTypes.ID, 0) AS BreaksTypeID,
		CASE WHEN (sp.DisplayName IS NULL) THEN CASE WHEN 
			 (sd.PreferredForename IS NULL or sd.PreferredForename = '''') THEN (sd.Forename + '''' + sd.Surname) ELSE
			 (sd.PreferredForename + '''' + sd.Surname)    END ELSE sp.DisplayName END AS FullName
               FROM             StaffDetails sd
			    INNER JOIN ScheduledPeople sp on sp.StaffDetailsID = sd.StaffID 
				INNER JOIN ScheduledPersonTeam_LINK spl on spl.ScheduledPersonID = sp.ScheduledPersonID and  spl.IsHomeTeam = 1  and convert(datetime,EndDate,110) >= convert(datetime,convert(varchar,GETDATE(),110),110)
								  and (
								  convert(datetime, StartDate, 110) >= convert(datetime, convert(varchar,GETDATE(),110), 110) or
								  isnull(convert(datetime,EndDate,110),''9999-01-01'') >= convert(datetime,convert(varchar(30),getdate(),110),110)
								  and convert(datetime,StartDate,110) <= convert(datetime,convert(varchar(30),getdate(),110),110))
               LEFT OUTER JOIN  BreaksTableTypes ON spl.TeamID = BreaksTableTypes.schedulingTeamId
			WHERE           (spl.TeamID = @intTeamID)
               ORDER BY           FullName
						
END'
EXEC dbo.sp_executesql @strSQL

GO
