USE [Allocate7]
GO
/****** Object:  Table [dbo].[StaffConfig]    Script Date: 27/02/2022 10:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND  TABLE_NAME = 'StaffContractHistory')
BEGIN
	CREATE TABLE [dbo].[StaffContractHistory](
		[ContractID] [int] NULL,
		[StaffID] [int] NULL,
		[EmpNumber] [int] NULL,
		[StartDate] [datetime]  NULL,
		[EndDate] [datetime] NULL,
		[ActualEndDate] [datetime] NULL,
		[DepartmentID] [int] NULL,
		[OrgID] [int] NULL,
		[OrgPositionID] [int] NULL,
		[CostCode] [varchar](8) NULL,
		[JobTitle] [varchar](40) NULL,
		[Grade] [int]  NULL,
		[ActingGrade] [int] NULL,
		[IsPartTime] [bit] NULL,
		[EFT] [decimal](4, 3) NULL,
		[HoursPerWeek] [decimal](4, 2) NULL,
		[WorkScheduleCode] [varchar](8) NULL,
		[ContractCode] [varchar](6) NULL,
		[PayArea] [varchar](2) NULL,
		[UPAWageType] [varchar](5) NULL,
		[UPACode] [int] NULL,
		[CreatedDate] [datetime] NULL,
		[LastModDate] [datetime] NULL,
		[LastModBy] [varchar](50) NULL,
		[LastModChanges] [varchar](max) NULL,
		[CriticalLevel] [tinyint] NULL,
		[CriticalFixed] [bit] NULL,
		[History] [varchar](max) NULL,
		[IsActive] [bit] NULL,
		[IsActivated] [bit] NULL,
		[EmpSubGroup] [varchar](2) NULL,
		[LastDepartmentID] [int] NULL
	)
END