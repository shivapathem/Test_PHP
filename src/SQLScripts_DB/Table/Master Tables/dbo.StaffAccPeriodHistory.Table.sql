USE [Allocate7]
GO
/****** Object:  Table [dbo].[StaffPosition]    Script Date: 25/06/2021 10:57:58 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND  TABLE_NAME = 'StaffAccPeriodHistory')
BEGIN
	CREATE TABLE [dbo].[StaffAccPeriodHistory](
		[AccPeriodID] [int] NULL,
		[StaffID] [int] NULL,
		[AccGroupID] [int] NULL,
		[AccType] [int] NULL,
		[StartDate] [datetime] NULL,
		[EndDate] [datetime] NULL,
		[StartWeek] [int] NULL,
		[EndWeek] [int] NULL,
		[IsActive] [bit] NULL,
		[CreatedDate] [datetime] NULL,
		[LastModDate] [datetime] NULL,
		[LastModBy] [varchar](50) NULL,
		[History] [varchar](max) NULL
	)
END
GO
SET ANSI_PADDING OFF
GO
