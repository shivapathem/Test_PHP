USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ScheduledPersonLeaveCredit]    Script Date: 15/09/2025 17:11:29 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE OR ALTER       PROCEDURE [dbo].[usp_get_ScheduledPersonLeaveCredit]
	-- Add the parameters for the stored procedure here
	@intleaveyear int,
	@scheduledpersonid int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    SELECT  cast(ISNULL(LA.Annual, 0) as float) AS Annual, 
            cast(ISNULL(LA.PHL, 0) as float) AS PHL, 
            cast(ISNULL(LA.Comp, 0) as float) AS Comp, 
            cast(ISNULL(LA.Additional, 0) as float) AS Additional, 
            cast(ISNULL(LA.Exceptional, 0) as float) AS Exceptional, 
            cast(ISNULL(LA.TOIL, 0) as float) AS TOIL,
            cast(ISNULL(LA.Under11TOIL, 0) as float) AS Under11TOIL, 
            cast(ISNULL(LA.Over12TOIL, 0) as float) AS Over12TOIL,
            cast(ISNULL(LA.Casual, 0) as float) AS Casual,
            cast(ISNULL(LA.LongService, 0) as float) AS LongService,
            cast(ISNULL(LA.Other, 0) as float) AS Other,
            LA.dDate, 
			LA.Comments, 
			ST.schedulingTeamName , 
			LA.ID,
			LA.IsCarryOver
       FROM LeaveAllocation LA (nolock)
       LEFT OUTER JOIN  schedulingTeams ST (nolock) ON ISNULL(LA.SchedulingTeamid, 0) = ST.schedulingTeamId
      WHERE (iYear = @intleaveyear)
        AND (SchedulingPersonID IN (@scheduledpersonid)) AND (LA.IsActive=1)
	  ORDER BY LA.dDATE,LA.CreatedDate,LA.ID ASC
END