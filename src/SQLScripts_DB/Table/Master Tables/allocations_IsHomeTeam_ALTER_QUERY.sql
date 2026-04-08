USE [Allocate7]
GO

/****** Object:  Table [dbo].[Allocations]    Script Date: 27/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[IsHomeTeam]'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD IsHomeTeam INT NULL DEFAULT 1
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[MarkWiad]'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD MarkWiad INT NULL DEFAULT 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[MarkActual]'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD MarkActual INT NULL DEFAULT 0
END

GO