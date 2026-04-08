USE [Allocate7]
GO
/****** Object:  Table [dbo].[Allocations_edit]    Script Date: 08/10/2021  ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO

IF  NOT EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[Allocations_edit]'))

BEGIN

CREATE TABLE [dbo].[Allocations_edit](
	[StaffNumber] [nvarchar](10) NULL,
	[DutyName] [nvarchar](50) NULL,
	[Duration] [float] NULL,
	[WeekNumber] [int] NOT NULL,
	[iDay] [smallint] NOT NULL,
	[StartTime] [float] NULL,
	[EndTime] [float] NULL,
	[DutyComments] [nvarchar](max) NULL,
	[PersonComments] [nvarchar](max) NULL,
	[AllocationID] [int] NULL,
	[History] [nvarchar](max) NULL,
	[editable] [bit] NULL,
	[isworking] [bit] NULL,
	[OrigAllocationID] [int] NULL,
	[deleted] [tinyint] NOT NULL,
	[DepartmentID] [int] NULL,
	[BackColour] [nvarchar](50) NULL,
	[FontColour] [nvarchar](50) NULL,
	[edited] [bit] NULL,
	[InternalEdited] [bit] NULL,
	[IsOriginal] [bit] NULL,
	[LastUpdate] [bigint] NULL,
	[canbedeleted] [bit] NULL,
	[BaseCode] [int] NULL,
	[zAllocateInstanceID] [int] NULL,
	[zID] [int] NULL,
	[zbase] [tinyint] NULL,
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[SchedulingTeamId] [int] NULL,
	[SchedulingPersonID] [int] NULL,
	[DutyDate] [datetime] NULL,
	[StartDate] [datetime] NULL,
	[EndDate] [datetime] NULL,
	[IsAttention] [bit] NULL,
 CONSTRAINT [PK_Allocations_edit] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

END