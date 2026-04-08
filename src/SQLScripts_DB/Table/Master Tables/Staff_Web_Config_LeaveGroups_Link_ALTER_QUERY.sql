USE [Allocate7]
GO

/****** Object:  Table [dbo].[Staff_Web_Config_LeaveGroups_Link]    Script Date: 03/03/2022 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ScheduledPersonID'
          AND Object_ID = Object_ID(N'[dbo].[Staff_Web_Config_LeaveGroups_Link]'))
BEGIN
ALTER TABLE [dbo].[Staff_Web_Config_LeaveGroups_Link] Add ScheduledPersonID int
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'IsActive'
          AND Object_ID = Object_ID(N'[dbo].[Staff_Web_Config_LeaveGroups_Link]'))
BEGIN
 
  alter table Staff_Web_Config_LeaveGroups_Link add IsActive  INT default 1;

END


GO