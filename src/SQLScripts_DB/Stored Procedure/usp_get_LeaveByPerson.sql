USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveByPerson]    Script Date: 30/06/2022 17:17:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_get_LeaveByPerson] 
	-- Add the parameters for the stored procedure here
	@scheduledPersonID INT,
	@startdate varchar (50),
	@enddate varchar (50)
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	
   		SELECT   CONVERT(DATE, Leave.StartingDate, 102) AS startdate, CONVERT(DATE, Leave.EndingDate, 102) AS enddate, CAST(ISNULL(Leave.Annual, 0) AS float) AS Annual, 
                CAST(ISNULL(Leave.PHL, 0) AS float) AS PHL, CAST(ISNULL(Leave.Comp, 0) AS float) AS Comp, CAST(ISNULL(Leave.Additional, 0) AS float) AS Additional, 
                CAST(ISNULL(Leave.Exceptional, 0) AS float) AS Exceptional, CAST(ISNULL(Leave.Under11TOIL, 0) AS float) AS Under11TOIL, CAST(ISNULL(Leave.Over12TOIL, 0) AS float)   AS Over12TOIL,
                CAST(ISNULL(Leave.Casual, 0) AS float) AS Casual,CAST(ISNULL(Leave.LongService, 0) AS float) AS LongService,CAST(ISNULL(Leave.Other, 0) AS float) AS Other,
              Leave.Comments, Leave.Status, Leave.ID
			FROM      Leave (nolock)
			WHERE   (Leave.StartingDate >= CONVERT(DATE, @startdate, 102)) AND (Leave.StartingDate <= CONVERT(DATE, @enddate, 102)) AND 
                (Leave.ScheduledPersonID IN (@scheduledPersonID)) AND (Leave.Status <> 2) 
					ORDER BY Leave.StartingDate
						

						
END