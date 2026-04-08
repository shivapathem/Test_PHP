USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingTeamId'
          AND Object_ID = Object_ID(N'[dbo].[AllocationsHiddenDays]'))
BEGIN
    Alter Table [dbo].[AllocationsHiddenDays] ADD SchedulingTeamId int;


END


GO
