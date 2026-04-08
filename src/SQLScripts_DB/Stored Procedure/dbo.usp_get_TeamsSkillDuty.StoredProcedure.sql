USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_TeamsSkillDuty]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'Procedure [dbo].[usp_get_TeamsSkillDuty]
@weekday int,
@TeamId int,
@bst int

AS
BEGIN
DECLARE
	@GMT int =0
	if (@bst != 1)
	BEGIN
		SET @GMT =1;
	END
	
END

BEGIN
if (@bst=1)
	BEGIN
		SELECT skills_duties.duty, skills_duties.description, skills_duties.id FROM skills_duties
		INNER JOIN skills_duties_days ON skills_duties.id = skills_duties_days.duty_id
		WHERE (skills_duties_days.dotw = @weekday) AND skills_duties.TeamID  = @TeamId AND (skills_duties.BST = @bst)
		ORDER BY skills_duties.duty
	END
else if (@GMT=1)
	BEGIN 
		SELECT skills_duties.duty, skills_duties.description, skills_duties.id FROM skills_duties
		INNER JOIN skills_duties_days ON skills_duties.id = skills_duties_days.duty_id
		WHERE (skills_duties_days.dotw = @weekday) AND skills_duties.TeamID  = @TeamId AND (skills_duties.GMT = @GMT)
		ORDER BY skills_duties.duty
	END
End
'
EXEC dbo.sp_executesql @strSQL


GO




