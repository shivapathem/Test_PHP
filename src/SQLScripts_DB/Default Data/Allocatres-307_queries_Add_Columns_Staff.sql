USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'PHLLeaveAmount' AND Object_ID = Object_ID(N'[dbo].[Staff]'))
BEGIN
  	ALTER TABLE Staff add PHLLeaveAmount FLOAT NULL;
END


GO