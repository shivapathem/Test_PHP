USE [Allocate7]
GO

IF NOT EXISTS (SELECT 1 FROM [dbo].[HistoryTypes] WHERE [HistoryType]='PublishWeek')
BEGIN
  DECLARE @idVal INT
  select @idVal = max(id)+1 from HistoryTypes
  INSERT [dbo].[HistoryTypes] ([id], [HistoryType]) VALUES (@idVal, 'PublishWeek')
END