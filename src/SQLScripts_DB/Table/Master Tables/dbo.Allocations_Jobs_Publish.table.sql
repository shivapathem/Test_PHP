USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

SET ANSI_PADDING ON
GO

IF NOT  EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[Allocations_Jobs_Publish]'))

CREATE TABLE [dbo].[Allocations_Jobs_Publish](
	[AllocateInstanceID] [int] NOT NULL,
	[DepartmentID] [int] NOT NULL,
	[AllocationID] [int] NOT NULL,
	[AllocateJobID] [int] NOT NULL,
	[StaffNumber] [nvarchar](10) NULL,
	[WeekNumber] [int] NULL,
	[iDay] [smallint] NOT NULL,
	[Programme] [varchar](60) NULL,
	[Contact] [varchar](60) NULL,
	[Location] [varchar](25) NULL,
	[JobName] [nvarchar](100) NULL,
	[StartTime] [float] NULL,
	[EndTime] [float] NULL,
	[JobBackColour] [varchar](12) NULL,
	[JobFontColour] [varchar](12) NULL,
	[Comments] [varchar](max) NULL,
	[MasterJobID] [int] NULL,
	[AdhocDuty] [bit] NULL,
	[UnAllocated] [bit] NULL,
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[SchedulingPersonID] [int] NULL,
	[ProgrammeId] [int] NULL,
	[JobDefaultColour] [nvarchar](12) NULL,
	[aftermidnight] [int] NULL,
	[schedulingTeamId] [int] NULL,
	[Edited] [bit] NULL,
	[isPublished] [int] NULL,
	[isEdited] [int] NULL,
	[isActive] [int] NULL,
	[Job_Info] [varchar](1000) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

SET ANSI_PADDING OFF
GO


