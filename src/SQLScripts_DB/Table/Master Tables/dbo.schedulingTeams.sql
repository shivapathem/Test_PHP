USE [Allocate7]
GO
/****** Object:  Table [dbo].[schedulingTeams]    Script Date: 30/06/2021 14:00:04 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[schedulingTeams](
	[schedulingTeamId] [int] IDENTITY(1,1) NOT NULL,
	[schedulingTeamName] [varchar](120) NULL,
	[schedulingTeamDescription] [nvarchar](550) NULL,
	[defaultDutyChargeCode] [varchar](10) NULL,
	[isActive] [int] NOT NULL,
	[defaultSicknessHoursAllocation] [int] NULL,
	[defaultDutyDuration] [int] NULL,
	[maskType] [varchar](50) NULL,
	[maskAfter] [int] NULL,
	[DailyViewMasking] [bit] NULL,
	[dailyViewMaskingDays] [int] NULL,
	[freelancerMasking] [bit] NULL,
	[freelancerMaskingDays] [int] NULL,
	[restrictedEditing] [bit] NULL,
	[numberofDaysAllowedEditing] [int] NULL,
	[WeekendOnly] [bit] NULL,
	[autoLockTodayTimer] [bit] NULL,
	[workTimeDirectiveOptOut] [bit] NULL,
	[checkOverSixDaysWorked] [bit] NULL,
	[checkOverFiveDaysWorked] [bit] NULL,
	[locks] [bit] NULL,
	[locksStart] [int] NULL,
	[locksWeekataTime] [bit] NULL,
	[locksEnd] [int] NULL,
	[signIn] [bit] NULL,
	[signInDays] [int] NULL,
	[allowInBuilding] [bit] NULL,
	[hasGridChecks] [bit] NULL,
	[autoImportWeeks] [bit] NULL,
	[NoofAutoAutoimportWeeks] [varchar](50) NULL,
	[showProductionView] [bit] NULL,
	[allowOvertimeRequests] [bit] NULL,
	[colourWeek] [bit] NULL,
	[defaultNumberweeksRotaPattern] [int] NULL,
	[defaultRotaStartDate] [datetime] NULL,
	[currentLeaveYear] [int] NULL,
	[leaveSelectiveHide] [bit] NULL,
	[hasHandovers] [bit] NULL,
	[hasXmasPoints] [bit] NULL,
	[staffAvailabilityReportStartDate] [datetime] NULL,
	[createdby] [int] NULL,
	[createddate] [datetime] NULL,
	[modifiedby] [int] NULL,
	[modifieddate] [datetime] NULL,
	[editingStart] [int] NULL,
	[editingEnd] [int] NULL,
	[history] [nvarchar](max) NULL,
	[divisionid] [int] NULL,
 CONSTRAINT [PK_schedulingTeams] PRIMARY KEY CLUSTERED 
(
	[schedulingTeamId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [isActive]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [defaultSicknessHoursAllocation]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [defaultDutyDuration]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [maskAfter]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [DailyViewMasking]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [dailyViewMaskingDays]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [freelancerMasking]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [freelancerMaskingDays]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [restrictedEditing]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [numberofDaysAllowedEditing]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [WeekendOnly]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [autoLockTodayTimer]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [workTimeDirectiveOptOut]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [checkOverSixDaysWorked]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [checkOverFiveDaysWorked]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [locks]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [locksStart]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [locksWeekataTime]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [locksEnd]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [signIn]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [signInDays]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [allowInBuilding]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [hasGridChecks]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [autoImportWeeks]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [showProductionView]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [allowOvertimeRequests]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [colourWeek]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [defaultNumberweeksRotaPattern]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [currentLeaveYear]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [leaveSelectiveHide]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [hasHandovers]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [hasXmasPoints]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [createdby]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [modifiedby]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [editingStart]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [editingEnd]
GO

ALTER TABLE [dbo].[schedulingTeams] ADD  DEFAULT ((0)) FOR [divisionid]
GO

