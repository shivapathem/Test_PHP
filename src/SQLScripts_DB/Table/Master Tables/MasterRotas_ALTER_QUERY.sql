USE [Allocate7]
GO

/****** Object:  Table [dbo].[MasterRotas]    Script Date: 11/11/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
--Created By Soniya  On 9th Nov 2021--
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'IsExported '
          AND Object_ID = Object_ID(N'[dbo].[MasterRotas]'))
BEGIN
ALTER TABLE MasterRotas Add IsExported bit default 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'ExportedOn '
          AND Object_ID = Object_ID(N'[dbo].[MasterRotas]'))
BEGIN
ALTER TABLE MasterRotas Add ExportedOn datetime default null
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'ExportedBy'
          AND Object_ID = Object_ID(N'[dbo].[MasterRotas]'))
BEGIN
ALTER TABLE MasterRotas Add ExportedBy int default 0
END




GO
