USE [Allocate7]
GO
/****** Object:  Table [dbo].[StaffPosition]    Script Date: 25/06/2021 10:57:58 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND  TABLE_NAME = 'StaffPositionHistory')
BEGIN
	CREATE TABLE [dbo].[StaffPositionHistory](
		[PositionID] [int] NULL,
		[StaffID] [int] NULL,
		[EmpNumber] [int] NULL,
		[StartDate] [datetime] NULL,
		[EndDate] [datetime] NULL,
		[CostCode] [varchar](8) NULL,
		[PersonnelOfficerCode] [varchar](3) NULL,
		[Level2] [varchar](8) NULL,
		[Level3] [varchar](8) NULL,
		[Level4] [varchar](8) NULL,
		[Level5] [varchar](8) NULL,
		[JobTitle] [varchar](30) NULL,
		[PersonnelArea] [varchar](4) NULL,
		[PersonnelSubArea] [varchar](4) NULL,
		[EmployeeGroup] [char](1) NULL,
		[EmployeeSubGroup] [varchar](2) NULL,
		[PayArea] [varchar](2) NULL,
		[Manager] [bit] NULL,
		[SubActingStatus] [bit] NULL,
		[SubstantiveCostCode] [varchar](10) NULL,
		[ActualPosition] [int] NULL,
		[HROrganisation] [int] NULL,
		[DelegatedPosition] [bit] NULL,
		[FreelancerMainID] [int] NULL,
		[ActionType] [varchar](2) NULL,
		[ActionReason] [varchar](2) NULL,
		[CreatedDate] [datetime] NULL,
		[LastModDate] [datetime] NULL,
		[LastModBy] [varchar](50) NULL,
		[LastModChanges] [varchar](max) NULL,
		[CriticalLevel] [tinyint] NULL,
		[History] [varchar](max) NULL,
		[IsActive] [bit] NULL
	)
END
GO
SET ANSI_PADDING OFF
GO
