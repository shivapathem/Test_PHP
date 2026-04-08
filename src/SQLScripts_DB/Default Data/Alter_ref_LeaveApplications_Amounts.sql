USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ReasonID'
          AND Object_ID = Object_ID(N'[dbo].[ref_LeaveApplications_Amounts]'))
BEGIN
  alter table ref_LeaveApplications_Amounts add ReasonID int;


END


GO