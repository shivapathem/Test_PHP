USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'LongService'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
 alter table LeaveAllocation add  LongService float(8);


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Other'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
 alter table LeaveAllocation add  Other float(8);


END
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Casual'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
 alter table LeaveAllocation add  Casual float(8);


END

IF EXISTS (SELECT 1 FROM LeaveAllocateTypes WHERE Description='Long Service')
BEGIN
  
  update LeaveAllocateTypes set AllocName='LongService' where Description='Long Service'

END

IF  NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'IsActive'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
  alter table LeaveAllocation add IsActive tinyint default 1;
END

IF  NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'CreatedDate'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
  alter table LeaveAllocation add CreatedDate datetime default NULL;
END

IF  NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'CreatedBy'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
  alter table LeaveAllocation add CreatedBy int default NULL;
END


IF  NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'UpdateDate'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
  alter table LeaveAllocation add UpdateDate datetime default NULL;
END

IF  NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'UpdatedBy'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
  alter table LeaveAllocation add UpdatedBy int default NULL;
END






GO