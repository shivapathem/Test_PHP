USE [Allocate7]
GO


SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

SET ANSI_PADDING ON
GO

IF NOT  EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[REF_AccountingGroup_Dates]'))


CREATE TABLE [dbo].[REF_AccountingGroup_Dates]
(
	  [AccGroupID] INT NOT NULL
	, [BBCWeek] INT NOT NULL
	, [WeekStartDateKey] INT NOT NULL
	, [WeekEndDateKey] INT NOT NULL
	, [WeekStartDate] DATETIME NULL
	, [WeekEndDate] DATETIME NULL
	, [AccPeriodStartWeek] INT NULL
	, [AccPeriodEndWeek] INT NULL
	, [AccPeriodStartDate] DATETIME NULL
	, [AccPeriodEndDate] DATETIME NULL
	, [AccPeriodWeeks] INT NULL
	, [WeekOfAccPeriod] INT NULL
	, [AccGroup] VARCHAR(20) COLLATE Latin1_General_CI_AS NULL
	, CONSTRAINT [PK_REF_AccountingGroup_Dates] PRIMARY KEY ([AccGroupID] ASC, [BBCWeek] ASC)
)


GO

SET ANSI_PADDING OFF
GO


