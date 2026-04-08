USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'isPublished'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
  alter table Allocations_jobs add isPublished int default 0;
END

GO