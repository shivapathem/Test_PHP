USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingPersonID'
          AND Object_ID = Object_ID(N'[dbo].[signin]'))
BEGIN
   alter table  signin add SchedulingPersonID INT


END

