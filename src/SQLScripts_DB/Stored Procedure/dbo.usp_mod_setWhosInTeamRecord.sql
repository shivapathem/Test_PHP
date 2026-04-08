USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_setWhosInTeamRecord]    Script Date: 15/07/2025 17:31:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_mod_setWhosInTeamRecord]
@personid int,
@teamid int,
@currentuserid int
AS
BEGIN

	SET NOCOUNT ON;

	DECLARE @teamname varchar(300), @UpdateDateTime datetime, @returnstring VARCHAR(1000), @historyMsg VARCHAR(1000), @historyUserName VARCHAR(300)
	SET @UpdateDateTime = GETDATE()

		SELECT @teamname = schedulingTeamName  FROM schedulingTeams WHERE schedulingTeamId = @teamid and isActive = 1
		select @historyUserName = UD_DisplayName from UserDetails where UD_UserID = @personid

		SET @returnstring = @teamname + ' has been unset from whos in scheduled team successfully.'
		SET @historyMsg = @teamname + ' has been unset from whos in by ' + @historyUserName + ' on ' + CONVERT(VARCHAR, @UpdateDateTime, 103) + ' at ' +SUBSTRING(CONVERT(VARCHAR, GETDATE(), 108), 1, 5)
		
		if EXISTS(select isWhosIn from ScheduledPersonTeam_LINK Where ScheduledPersonID = @personid and TeamID = @teamid)
		BEGIN
			SET @returnstring = @teamname + ' has been set as whos in scheduled team successfully.'
			SET @historyMsg = @teamname + ' has been set as whos in by ' + @historyUserName + ' on ' + CONVERT(VARCHAR, @UpdateDateTime, 103) + ' at ' +SUBSTRING(CONVERT(VARCHAR, GETDATE(), 108), 1, 5)
		END
		Update ScheduledPersonTeam_LINK SET isWhosIn = isWhosIn ^ 1 Where ScheduledPersonID = @personid and TeamID = @teamid

		INSERT INTO [dbo].[AdditionalPermissionsHistory]([HistoryType],[UserID],[History],[datetime],[AttributeID],[AttributeID2])
		VALUES (5 ,@currentuserid,@historyMsg,@UpdateDateTime,@teamid,@personid)
		SELECT 'success' strstatus , @returnstring strreturnstring;
END