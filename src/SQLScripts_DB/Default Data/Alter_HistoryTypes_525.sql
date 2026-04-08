USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT 1 FROM [dbo].[HistoryTypes] WHERE [HistoryType]='Daily Allocations - UnAllocated Screen')
BEGIN
  UPDATE [dbo].[HistoryTypes] SET [HistoryType]='AllocationDuty' WHERE [HistoryType]='Daily Allocations - UnAllocated Screen'
END

IF NOT EXISTS (SELECT 1 FROM [dbo].[HistoryTypes] WHERE [HistoryType]='AllocationDuty')
BEGIN
  INSERT [dbo].[HistoryTypes] ([id], [HistoryType]) VALUES (8, 'AllocationDuty')
END

IF NOT EXISTS (SELECT 1 FROM [dbo].[HistoryTypes] WHERE [HistoryType]='AllocationJobs')
BEGIN
  INSERT [dbo].[HistoryTypes] ([id], [HistoryType]) VALUES (9, 'AllocationJobs')
END


GO