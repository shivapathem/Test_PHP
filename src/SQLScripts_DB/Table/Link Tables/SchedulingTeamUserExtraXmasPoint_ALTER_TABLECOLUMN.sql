USE [Allocate7]
GO

/****** Object:  Table [dbo].[SchedulingTeamUserExtraXmasPoint]    Script Date: 02/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'SchedulingTeamUserExtraXmasPoint' AND COLUMN_NAME = 'UserId')
BEGIN
    EXEC sp_RENAME 'SchedulingTeamUserExtraXmasPoint.UserId' , 'ScheduledPersonID', 'COLUMN'
END
GO
