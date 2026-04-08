USE [Allocate7]
GO
/****** Object:  Table [dbo].[StaffConfig]    Script Date: 27/02/2022 10:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND  TABLE_NAME = 'StaffConfigHistory')
BEGIN
	CREATE TABLE [dbo].[StaffConfigHistory](
		[ConfigID] [int] NULL,
		[StaffID] [int] NULL,
		[StartDate] [datetime] NULL,
		[EndDate] [datetime] NULL,
		[ManualEDP] [bit] NULL,
		[EDPMinimum] [decimal](6, 2) NULL,
		[EDPMinimumExcBreaks] [decimal](6, 2) NULL,
		[PartTimeEDP] [decimal](6, 2) NULL,
		[ShiftBreak] [tinyint] NULL,
		[PTCalcBreak] [tinyint] NULL,
		[PTCalcLeave] [tinyint] NULL,
		[AccGroupID] [int] NULL,
		[AveDayLen] [decimal](4, 2) NULL,
		[AccDays] [decimal](5, 2) NULL,
		[TermsCondsVersionID] [int] NULL,
		[PaymentTypeID] [int] NULL,
		[BreaksGroupID] [int] NULL,
		[CompExpiry] [int] NULL,
		[TOILExpiry] [int] NULL,
		[Under11TOILExpiry] [int] NULL,
		[Over12TOILExpiry] [int] NULL,
		[ActivityTypeID] [int] NULL,
		[ActivityType] [varchar](11) NULL,
		[CreatedDate] [datetime] NULL,
		[LastModDate] [datetime] NULL,
		[LastModBy] [varchar](100) NULL,
		[LastModChanges] [varchar](max) NULL,
		[CriticalLevel] [tinyint] NULL,
		[History] [varchar](max) NULL,
		[IsActive] [bit] NULL,
		[AutoEDPTOIL] [bit] NULL,
		[SendEmails] [bit] NULL,
	)
END
GO

