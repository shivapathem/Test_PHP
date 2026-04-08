USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ScheduledPersonsByTeamWithSortorder]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_ScheduledPersonsByTeamWithSortorder]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE dbo.usp_get_ScheduledPersonsByTeamWithSortorder 
	-- Add the parameters for the stored procedure here
	@sortorder INT,
	@schedulingteamid INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	IF @sortorder = 0
	BEGIN
			
			Select SD.StaffNumber, ISNULL(SD.NetLogin, '''') as Login, ISNULL(SPL.fontcolour, N''#000000'') AS TextColour 
			from ScheduledPersonTeam_LINK SPL 
			INNER JOIN ScheduledPeople SP ON SP.ScheduledPersonID = SPL.ScheduledPersonID
			LEFT JOIN StaffDetails SD ON SD.StaffID = SP.StaffDetailsID 
			where SPL.TeamID IN (@schedulingteamid)  
			ORDER BY SD.Surname, SD.Forename
	END
	ELSE
	BEGIN
	Select SD.StaffNumber, ISNULL(SD.NetLogin, '''') as Login, ISNULL(SPL.fontcolour, N''#000000'') AS TextColour 
			from ScheduledPersonTeam_LINK SPL 
			INNER JOIN ScheduledPeople SP ON SP.ScheduledPersonID = SPL.ScheduledPersonID
			LEFT JOIN StaffDetails SD ON SD.StaffID = SP.StaffDetailsID 
			where SPL.TeamID IN (@schedulingteamid)  
			
	END
END
'
EXEC dbo.sp_executesql @strSQL

GO
