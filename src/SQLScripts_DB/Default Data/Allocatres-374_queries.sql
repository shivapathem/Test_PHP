USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ProgrammeId'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
    alter table Allocation_jobs_edit add ProgrammeId int 


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'JobDefaultColour'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
    alter table Allocation_jobs_edit add JobDefaultColour varchar(10) 


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'aftermidnight'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
    alter table Allocation_jobs_edit add aftermidnight int 


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'unallocated'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
    alter table Allocation_jobs_edit add unallocated int  


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Contact'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
   alter table Allocation_jobs_edit add Contact varchar(60)


END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Location'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
   alter table Allocation_jobs_edit add Location varchar(25)


END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ProgrammeId'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_Jobs]'))
BEGIN
    alter table Allocations_Jobs add ProgrammeId int 


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'JobDefaultColour'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_Jobs]'))
BEGIN
   alter table Allocations_Jobs add JobDefaultColour varchar(10)


END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'aftermidnight'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_Jobs]'))
BEGIN
   alter table Allocations_Jobs add aftermidnight int 


END


GO
