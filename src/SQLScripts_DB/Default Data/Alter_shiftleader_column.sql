USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'aftermidnight' AND Object_ID = Object_ID(N'[dbo].[shiftleaders]'))
BEGIN
  	ALTER TABLE shiftleaders add aftermidnight INT;
END


GO