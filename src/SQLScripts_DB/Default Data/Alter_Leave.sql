USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Casual'
          AND Object_ID = Object_ID(N'[dbo].[Leave]'))
BEGIN
  alter table Leave add Casual float default NULL;


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'LongService'
          AND Object_ID = Object_ID(N'[dbo].[Leave]'))
BEGIN
 
  alter table Leave add LongService float default NULL;

END
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Other'
          AND Object_ID = Object_ID(N'[dbo].[Leave]'))
BEGIN
 
  alter table Leave add Other  float default NULL;

END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ScheduledPersonID'
          AND Object_ID = Object_ID(N'[dbo].[Leave]'))
BEGIN
 
  alter table Leave add ScheduledPersonID  INT default 0;

END

GO