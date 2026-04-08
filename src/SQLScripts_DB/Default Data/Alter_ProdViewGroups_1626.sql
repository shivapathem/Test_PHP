USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingTeamId'
          AND Object_ID = Object_ID(N'[dbo].[ProdViewGroups]'))
BEGIN
  alter table ProdViewGroups add SchedulingTeamId int default 0;
END

GO