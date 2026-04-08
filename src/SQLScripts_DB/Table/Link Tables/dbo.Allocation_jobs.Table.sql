USE [Allocate7]
GO
/****** Object:  Table [dbo].[Allocations_jobs]    Script Date: 08/10/2021  ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO


IF  NOT EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[Allocations_jobs]'))

BEGIN

CREATE TABLE [dbo].[Allocations_jobs](
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
	[JobBackColour] [bigint] NULL,
	[JobFontColour] [bigint] NULL,
	[Comments] [varchar](max) NULL,
	[MasterJobID] [int] NULL,
	[AdhocDuty] [bit] NULL,
	[UnAllocated] [bit] NULL,
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[schedulingTeamId] [int] NULL,
	[SchedulingPersonID] [int] NULL,
	[isActive] [bit] NOT NULL,
 CONSTRAINT [PK_Allocations_jobs] PRIMARY KEY CLUSTERED 
(
	[AllocateInstanceID] ASC,
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]


END