USE [Allocate7]
GO

/****** Object:  Table [dbo].[Allocations]    Script Date: 29/07/2024  ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyProgramId2'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
    alter table Allocations add DutyProgramId2 INT NULL;
END;

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyProgramId3'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
    alter table Allocations add DutyProgramId3 INT NULL;
END;

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyProgramId4'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
    alter table Allocations add DutyProgramId4 INT NULL;
END;

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyProgramId5'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
    alter table Allocations add DutyProgramId5 INT NULL;
END;

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyProgramId6'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
    alter table Allocations add DutyProgramId6 INT NULL;
END;

GO