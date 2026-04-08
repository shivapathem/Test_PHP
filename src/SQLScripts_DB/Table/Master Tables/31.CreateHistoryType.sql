USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

SET DATEFORMAT YMD

BEGIN

 IF NOT EXISTS ( SELECT 1 FROM HistoryTypes WHERE HistoryType = 'AllocationScheduledPerson' )
	insert into HistoryTypes (id,HistoryType) values(17,'AllocationScheduledPerson')
	
END
GO
