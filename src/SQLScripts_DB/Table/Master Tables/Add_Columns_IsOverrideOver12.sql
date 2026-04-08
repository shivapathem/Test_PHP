USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsOverrideOver12' AND Object_ID = Object_ID(N'[dbo].[masterduties]'))
BEGIN
  	ALTER TABLE masterduties add IsOverrideOver12 BIT default 1;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsOverrideOver12' AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
  	ALTER TABLE Allocations add IsOverrideOver12 BIT default 1;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsOverrideOver12' AND Object_ID = Object_ID(N'[dbo].[Allocations_archive]'))
BEGIN
  	ALTER TABLE Allocations_archive add IsOverrideOver12 BIT default 1;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsOverrideOver12' AND Object_ID = Object_ID(N'[dbo].[Allocations_removed]'))
BEGIN
  	ALTER TABLE Allocations_removed add IsOverrideOver12 BIT default 1;
END

GO