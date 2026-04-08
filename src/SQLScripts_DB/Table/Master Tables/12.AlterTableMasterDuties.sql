USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS( SELECT 1 FROM sys.columns 
			WHERE Name  IN ('ScheduledPersonID','DutyComment') AND Object_ID = Object_ID(N'[dbo].[masterduties]'))
 BEGIN

	alter table masterduties add ScheduledPersonID INT, DutyComment VARCHAR(1000), isPublished BIT 

 END 