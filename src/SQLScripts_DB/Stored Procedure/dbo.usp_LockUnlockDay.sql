USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_LockUnlockDay]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_LockUnlockDay]
@history varchar(255),
@strDate varchar(255),
@intSchedulingTeamId INT,
@intAction INT


AS
BEGIN
	SET NOCOUNT ON;

	IF EXISTS (SELECT        id
                         FROM            released_days
                         WHERE        (dDate = CONVERT(DATETIME, @strDate, 102)) AND (schedulingTeamId = @intSchedulingTeamId))
             UPDATE    released_days
               SET       history = CONCAT(ISNULL(History, ''''), N''@history''), 
                         status = @intAction
               WHERE     (schedulingTeamId = @intSchedulingTeamId) 
               AND (dDate = CONVERT(DATETIME, @strDate, 102)) 
             ELSE    
               INSERT  INTO  released_days(dDate, schedulingTeamId, history, status)
               VALUES        (CONVERT(DATETIME, @strDate, 102), @intSchedulingTeamId, N''@history'', @intAction)
   
END
'

EXEC dbo.sp_executesql @strSQL

GO

