USE [Allocate7]
GO

CREATE TABLE AccPeriodROTADurationSummary (
	 AccPeriodROTASummaryID [int] IDENTITY(1,1) NOT NULL,
	 ScheduledPersonID INT NOT NULL,
	 AccPeriodStartDate DATE , 
	 AccPeriodEndDate DATE, 
	 AccPeriodStartWeek INT, 
	 AccPeriodEndWeek INT,
	 AccPeriodDuration INT,
	 AccPeriodDays INT,
	[CreatedBy] [int] NULL,
	[CreatedDate] [datetime] NULL,
	[LastModDate] [datetime] NULL,
	[LastModBy] [int] NULL,
CONSTRAINT [PK_AccPeriodROTASummaryID_1] PRIMARY KEY CLUSTERED 
(
	AccPeriodROTASummaryID ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
 
 
CREATE NONCLUSTERED INDEX idx_AccPeriodROTADurationSummary_IDX1 ON AccPeriodROTADurationSummary
(
	ScheduledPersonID,
	AccPeriodStartDate,
	AccPeriodEndDate
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO