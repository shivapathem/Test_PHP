USE [Allocate7]
GO



/****** Object: Table [dbo].[StaffAccPeriod] Script Date: 08/03/2022 17:26:37 ******/
SET ANSI_NULLS ON
GO



SET QUOTED_IDENTIFIER ON
GO


IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND  TABLE_NAME = 'StaffAccPeriod')
BEGIN
	CREATE TABLE [dbo].[StaffAccPeriod](
		[AccPeriodID] [int] IDENTITY(1,1) NOT NULL,
		[StaffID] [int] NOT NULL,
		[PeriodID] [int] NOT NULL,
		[AccType] [int] NULL,
		[StartDate] [datetime] NOT NULL,
		[EndDate] [datetime] NULL,
		[StartWeek] [int] NULL,
		[EndWeek] [int] NULL,
		[IsTimesheetSent] [bit] NULL,
		[IsAccounted] [bit] NULL,
		[IsOpen] [bit] NULL,
		[IsActive] [bit] NULL,
		[CreatedDate] [datetime] NULL,
		[LastModDate] [datetime] NULL,
		[LastModBy] [varchar](50) NULL,
		[History] [varchar](max) NULL,
		[IsSchedulerVerified] [bit] NULL,
		[DepartmentID] [int] NULL
	) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END


GO