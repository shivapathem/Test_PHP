USE [Allocate7]
GO

SET ANSI_NULLS ON
GO 

SET QUOTED_IDENTIFIER ON
GO 

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'IsPartDayLeaveAllowed '
          AND Object_ID = Object_ID(N'[dbo].[LeaveRequestGroups ]'))
BEGIN
ALTER TABLE LeaveRequestGroups ADD IsPartDayLeaveAllowed BIT NULL DEFAULT 0

UPDATE LeaveRequestGroups set IsPartDayLeaveAllowed = 0
END

GO