USE [Allocate7]
GO

/****** Object:  Table [dbo].[RequestTypes]    Script Date: 27/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'AffectsOthers'
          AND Object_ID = Object_ID(N'[dbo].[RequestTypes]'))
BEGIN
ALTER TABLE [dbo].[RequestTypes] ADD AffectsOthers INT NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'SendEmails'
          AND Object_ID = Object_ID(N'[dbo].[RequestTypes]'))
BEGIN
ALTER TABLE [dbo].[RequestTypes] ADD SendEmails DATETIME NULL DEFAULT 0
END

GO
