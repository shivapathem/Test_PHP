USE [Allocate7]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   FUNCTION [dbo].[Get_SchedulingPersonName]
(
  @pScreenName     VARCHAR(100),
  @pSchedulingPersonID INT
)
RETURNS VARCHAR(255)
AS
BEGIN
  DECLARE @displayNameSchPerson VARCHAR(255)
  
  IF(@pScreenName = 'EditWeekly')
    BEGIN
      SELECT @displayNameSchPerson = ( CASE
          WHEN ( sp.displayname IS NULL ) THEN
            CASE
            WHEN ( sd.preferredforename IS NULL OR sd.preferredforename = '''' ) THEN 
            (sd.forename + '''' + sd.surname )
            ELSE ( sd.preferredforename + '''' + sd.surname )
            END
          ELSE sp.displayname
          END )
      FROM ScheduledPeople sp (nolock)
      LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid
      WHERE sp.ScheduledPersonID = @pSchedulingPersonID
    END
  ELSE
    BEGIN
      SELECT @displayNameSchPerson = ( CASE
          WHEN ( sp.displayname IS NULL ) THEN
            CASE
            WHEN ( sd.preferredforename IS NULL OR sd.preferredforename = '''' ) THEN 
            (sd.forename + '''' + sd.surname )
            ELSE ( sd.preferredforename + '''' + sd.surname )
            END
          ELSE sp.displayname
          END )
      FROM ScheduledPeople sp (nolock)
      LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid
      WHERE sp.ScheduledPersonID = @pSchedulingPersonID
    END

  RETURN @displayNameSchPerson
END 
