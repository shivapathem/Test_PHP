USE [Allocate7]
GO

/****** Object:  Table [dbo].[LeaveAllocation]    Script Date: 11/10/2021 16:06:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'is_PHL'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
ALTER TABLE [dbo].[LeaveAllocation] ADD is_PHL INT Default 0;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'IsCarryOver'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
ALTER TABLE [dbo].[LeaveAllocation] ADD IsCarryOver bit Default 0;
END




GO