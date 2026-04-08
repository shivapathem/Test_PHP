USE [Allocate7]
GO

update History set HistorySubType = 'PH' Where HistoryType = 8 and HistorySubType = 'CH'

update History set HistorySubType = 'CH' Where HistoryType = 8 and HistorySubType is null