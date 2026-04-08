USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'SortCodeMappingID' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add SortCodeMappingID INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'EFT' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add EFT INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'TeamID' AND Object_ID = Object_ID(N'[dbo].[SortCodeMapping]'))
BEGIN
  	ALTER TABLE SortCodeMapping add TeamID INT;
END

GO