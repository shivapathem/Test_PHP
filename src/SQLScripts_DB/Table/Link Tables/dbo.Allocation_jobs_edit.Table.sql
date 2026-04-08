USE [Allocate7]
GO
/****** Object:  Table [dbo].[Allocation_jobs_edit]    Script Date: 08/10/2021  ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO


IF  NOT EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[Allocation_jobs_edit]'))

BEGIN

CREATE TABLE [dbo].[Allocation_jobs_edit](
	[AllocateJobID] [int] NULL,
	[DepartmentID] [int] NULL,
	[StaffNumber] [nvarchar](10) NULL,
	[AllocationID] [int] NULL,
	[WeekNumber] [int] NULL,
	[iDay] [smallint] NULL,
	[Programme] [nvarchar](60) NULL,
	[JobName] [nvarchar](50) NULL,
	[StartTime] [float] NULL,
	[EndTime] [float] NULL,
	[JobBackColour] [varchar](10) NULL,
	[JobFontColour] [varchar](10) NULL,
	[MasterJobID] [int] NULL,
	[Comments] [nvarchar](max) NULL,
	[history] [nvarchar](max) NULL,
	[OrigAllocationID] [int] NULL,
	[deleted] [bit] NULL,
	[canbedeleted] [bit] NULL,
	[Edited] [bit] NULL,
	[IsOriginal] [bit] NULL,
	[LastUpdate] [bigint] NULL,
	[zAllocateInstanceID] [int] NULL,
	[zID] [int] NULL,
	[zbase] [tinyint] NULL,
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[schedulingTeamId] [int] NULL,
	[SchedulingPersonID] [int] NULL,
	[ProgrammeId] [int] NULL,
	[JobDefaultColour] [varchar](10) NULL,
	[aftermidnight] [int] NULL,
	[unallocated] [int] NULL,
	[Contact] [varchar](60) NULL,
	[Location] [varchar](25) NULL,
	[info] [varchar](1000) NULL,
 CONSTRAINT [PK_Allocation_jobs_edit] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]


END