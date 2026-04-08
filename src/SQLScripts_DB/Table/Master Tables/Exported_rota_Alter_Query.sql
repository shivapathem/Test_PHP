USE [Allocate7]
GO

/****** Object:  Table [dbo].[Exported_rota]    Script Date: 07/12/2021 16:09:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'RosterStartWeek'
          AND Object_ID = Object_ID(N'[dbo].[Exported_rota]'))
BEGIN
ALTER TABLE [dbo].[Exported_rota] ADD RosterStartWeek VARCHAR(10)
END

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'StaffBackColour'
          AND Object_ID = Object_ID(N'[dbo].[Exported_rota]'))
BEGIN
ALTER TABLE [dbo].[Exported_rota] DROP COLUMN StaffBackColour
END

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyColorID'
          AND Object_ID = Object_ID(N'[dbo].[Exported_rota]'))
BEGIN
ALTER TABLE [dbo].[Exported_rota] DROP COLUMN DutyColorID
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'StaffBackColour'
          AND Object_ID = Object_ID(N'[dbo].[Exported_rota]'))
BEGIN
ALTER TABLE [dbo].[Exported_rota] ADD StaffBackColour VARCHAR(50)
END

GO