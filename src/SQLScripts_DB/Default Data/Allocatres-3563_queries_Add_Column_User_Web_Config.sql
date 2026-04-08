USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'ShiftCountingFilter' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add ShiftCountingFilter INT;
END

GO