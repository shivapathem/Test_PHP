USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'isPublished'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
  alter table Allocations add isPublished int;


END


GO