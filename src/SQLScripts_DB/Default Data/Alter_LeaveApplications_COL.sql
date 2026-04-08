USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO



IF  EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'OfficeComments'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN

ALTER TABLE dbo.LeaveApplications ALTER COLUMN OfficeComments nvarchar (500); 

END

IF  EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Totalhrs'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
 ALTER TABLE LeaveApplications ALTER COLUMN Totalhrs float 
END

GO