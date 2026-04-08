USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_GetAllocationsHideDay]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_GetAllocationsHideDay]
@intTeamID INT,
@strDate VARCHAR(10),
@intaction INT,
@strHideHistory VARCHAR(1000),
@strShowHistory VARCHAR(1000)

AS
BEGIN
	SET NOCOUNT ON;
IF (@intaction = 1) 
	BEGIN
	IF EXISTS (SELECT id FROM AllocationsHiddenDays WHERE (SchedulingTeamId = @intTeamID) AND (dDate = CONVERT(DATETIME, @strDate, 102))) 
               BEGIN
			   UPDATE AllocationsHiddenDays SET isRestricted = 1, History = CONCAT(ISNULL(History,''''), @strHideHistory)
			   WHERE (SchedulingTeamId = @intTeamID) AND (dDate = CONVERT(DATETIME, @strDate, 102)) 
			END			
			ELSE 
			BEGIN
			   INSERT INTO AllocationsHiddenDays (History, SchedulingTeamId, dDate) 
			   VALUES (@strHideHistory, @intTeamID, CONVERT(DATETIME, @strDate, 102)) 
			   END
	END
	else 
	BEGIN
	UPDATE AllocationsHiddenDays SET isRestricted = 0, History = CONCAT(ISNULL(History,''''), @strShowHistory)  
	WHERE (SchedulingTeamId = @intTeamID) AND (dDate = CONVERT(DATETIME, @strDate, 102))
	END		
		
END
'

EXEC dbo.sp_executesql @strSQL

GO

